--[[
    Скрипт получающий тизеры из редис
]]--

local sp = require "provider"
local get = require "get"

-- Подключение тизеров партнеров
local function renderExternal(count, block_id, encrypted_platform_id)
    ngx.say("window['TT'].loadJs('" .. sp.external_teasers_url .. "/" .. count .. "?b=" .. block_id .. "&p=" .. encrypted_platform_id .. "')")
end

-- Генерирует вывод для тизеров
local function renderTeasers(platform_id, encrypted_platform_id, teasers_ids, host, amountOfDisplaying)
    sp:redis():init_pipeline()
    for news_id, teaser_id in pairs(teasers_ids) do
        sp:redis():get(sp:getKey('teaser_html', teaser_id))
    end

    -- Если получен пустой ответ завершаем работу
    local results, err = sp:redis():commit_pipeline()
    if not results then
        ngx.exit(ngx.HTTP_OK)
    end

    local id = "ttarget_div"
    if ngx.var.arg_id ~= nil then
        id = ngx.var.arg_id
    end

    local args    = ".." .. encrypted_platform_id

    local teasers = ""
    local count = 0;
    for i = 1, #results do
        if results[i] ~= ngx.null then
            count = count + 1
            if host ~= "tt.ttarget.ru" then
                results[i] = results[i]:gsub("<img src=\"http://tt.ttarget.ru", "<img src=\"http://" .. host)
            end
            teasers = teasers .. results[i]:gsub("{args}", args)
        end
    end

    teasers = teasers .. '<img src="' .. sp.pixel_url .. '" style="display:none">'

    if ngx.var.renderer_version == "1" then
        ngx.say(string.format(
            "e=document.getElementById('%s');if(e)e.innerHTML='%s';if(typeof document.onscroll == 'function')document.onscroll();",
            id,
            teasers
        ));
    else
        ngx.say(string.format(
            "document.write('%s');if(typeof document.onscroll == 'function')document.onscroll();",
            teasers
        ));
    end

    if count < amountOfDisplaying then
        renderExternal(amountOfDisplaying - count, id, encrypted_platform_id)
    end
end

-- Основная программа
local country_code        = ngx.var.country_code
local city_id             = ngx.var.city_id
local platform_id         = sp:platform():getIdByHost(sp:platform():getHostByReferer(ngx.var.http_referer))
local encrypted_platform_id = sp:platform():getEIdOrId(platform_id)
local version             = sp:getVersion()
local amountOfDisplaying  = get:calcAmountOfDisplaying(tonumber(ngx.var.arg_w) or 0, tonumber(ngx.var.arg_h) or 0)

if platform_id and encrypted_platform_id and amountOfDisplaying then
    --sp:session():start();
    local teasers_ids = get:getTeasersIds(sp, version, platform_id, country_code, city_id, amountOfDisplaying)
    renderTeasers(platform_id, encrypted_platform_id, teasers_ids, ngx.req.get_headers()['host'], amountOfDisplaying)
    get:incrScore(sp, version, platform_id, country_code, city_id, teasers_ids)
    sp:platform():addRequestLog(platform_id);
end

sp:close()