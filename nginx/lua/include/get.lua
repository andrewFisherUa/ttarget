local M = {}

local teaserWidth   = 240  -- Ширина одного тизера
local teaserHeigth  = 100 -- Высота одного тизера

-- Вызывает загруженный LUA скрипт реализующий случайную выборку с учетом весов
local function weightedRandom(sp, key1, key2, limit)
    local sha = "d66aa7021a5134ab302a1ce5cd2747427d2c0140"
    local seed = tonumber(tostring(ngx.now()*1000):reverse():sub(1,6))
    return sp:redis():evalsha(sha, 2, key1, key2, limit, seed)
end

-- возвращает ключ новостей
local function getNewsKey(platform_id, country_code, city_id)
    local newsKey
    if city_id == '0' then
        newsKey = string.format("ttarget:platforms:%u:countries:%s:news", platform_id, country_code)
    else
        newsKey = string.format("ttarget:platforms:%u:cities:%u:news", platform_id, city_id)
    end
    return newsKey
end

-- возвращает ключ кампаний
local function getCampaignsKey(platform_id, country_code, city_id)
    local key = ''
    if city_id == '0' then
        key = string.format("ttarget:platforms:%u:countries:%s:campaigns", platform_id, country_code)
    else
        key = string.format("ttarget:platforms:%u:cities:%u:campaigns", platform_id, city_id)
    end
    return key
end

-- возвращает идентификаторы тизеров для показа (новый механизм)
local function getTeasersIdsByCampaigns(sp, platform_id, campaigns_ids, amountOfDisplaying, random)
    local campaignsCount = #campaigns_ids
    local teasers_ids = {}
    for i=1, campaignsCount do
        key = string.format("ttarget:platforms:%u:campaigns:%u:teasers", platform_id, campaigns_ids[i])
        local requestTeasers = math.floor((amountOfDisplaying - #teasers_ids) / (campaignsCount - i + 1))
        local teasers
        if random then
            teasers = sp:redis():srandmember(key, requestTeasers)
        else
            teasers = sp:redis():sort(key, "BY", "ttarget:teasers:*:score->score", "DESC", "LIMIT", "0", requestTeasers)
        end

        for t=1, #teasers do
            table.insert(teasers_ids, teasers[t])
        end
    end

    return teasers_ids
end

-- возвращает идентификаторы новостей для показа
local function getNewsIds(sp, amountOfDisplaying, platform_id, country_code, city_id)
    local key = getNewsKey(platform_id, country_code, city_id)
    local news_ids = sp:redis():zrange(key, 0, amountOfDisplaying - 1)
    if table.getn(news_ids) > 0 then
        return news_ids
    end

    ngx.exit(ngx.HTTP_OK)
end

-- возвращает идентификаторы кампаний для показа
local function getCampaignsIds(sp, platform_id, country_code, city_id, amountOfDisplaying, random)
    if amountOfDisplaying > 0 then
        local key = getCampaignsKey(platform_id, country_code, city_id)
        local campaigns_ids
        if random then
            campaigns_ids = sp:redis():srandmember(key, amountOfDisplaying)
        else
            campaigns_ids = weightedRandom(sp, key, 'ttarget:campaigns', amountOfDisplaying)
        end
        if table.getn(campaigns_ids) > 0 then
            return campaigns_ids
        end
    end
    return {}
end

-- Возвращает идентификаторы тизеров, которые будут показаны
local function getTeasersIdsByNews(sp, platform_id, news_ids)
    sp:redis():init_pipeline()
    for i = 1, #news_ids do
        key = string.format("ttarget:platforms:%u:news:%u:teasers", platform_id, news_ids[i])
        sp:redis():zrange(key, 0, 0)
    end

    -- Если количество полученных тизеров не равно количеству новостей или получен пустой ответ
    -- тогда завершаем работу nginx
    local results, err = sp:redis():commit_pipeline()
    if not results or table.getn(results) ~= table.getn(news_ids) then
        ngx.exit(ngx.HTTP_OK)
    end

    local teasers_ids = {}
    for i = 1, #results do
        teasers_ids[news_ids[i]] = results[i][1]
    end
    return teasers_ids
end

-- Увеличивает показы новостей и тизеров, чтобы обеспечить ротацию показов тизеров
function M.incrScore(self, sp, version, platform_id, country_code, city_id, teasers_ids)
    if version == '0' and not sp:isPilferer() then
        local newsKey = getNewsKey(platform_id, country_code, city_id)
        sp:redis():init_pipeline()

        for news_id, teaser_id in pairs(teasers_ids) do
            -- увеличивает показы для новостей
            sp:redis():zincrby(newsKey, 1, news_id)

            -- увеличивает показы тизеров
            local teaserKey = string.format("ttarget:platforms:%u:news:%u:teasers", platform_id, news_id)
            sp:redis():zincrby(teaserKey, 1, teaser_id)
        end

        sp:redis():commit_pipeline()
    end

end

-- расчитывает количество тизеров для показа
function M.calcAmountOfDisplaying(self, totalWidth, totalHeight)
    if totalWidth < teaserWidth or totalHeight < teaserHeigth then
        return nil
    end

    return math.floor(totalWidth / teaserWidth) * math.floor(totalHeight / teaserHeigth)
end

-- Осуществляет выборку тизеров
function M.getTeasersIds(self, sp, version, platform_id, country_code, city_id, amountOfDisplaying)
    local teasers_ids
    if version == '0' then
        local news_ids  = getNewsIds(sp, amountOfDisplaying, platform_id, country_code, city_id)
        teasers_ids = getTeasersIdsByNews(sp, platform_id, news_ids)
    elseif version == '2' then
        local campaigns_ids = getCampaignsIds(sp, platform_id, country_code, city_id, amountOfDisplaying, true)
        teasers_ids = getTeasersIdsByCampaigns(sp, platform_id, campaigns_ids, amountOfDisplaying, true)
    else
        local campaigns_ids = getCampaignsIds(sp, platform_id, country_code, city_id, amountOfDisplaying)
        teasers_ids = getTeasersIdsByCampaigns(sp, platform_id, campaigns_ids, amountOfDisplaying)
    end

    return teasers_ids
end

return M