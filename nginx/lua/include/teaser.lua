local M = {}

local null = ngx.null

-- Увеличивает показы по тизеру
function M.incrShows(self, platform_id, teasers_ids, city_id, country_code)
    local teaser_id
    for _, teaser_id in pairs(teasers_ids) do
        self.sp:redis():incr(self.sp:getKey('shows_counter', platform_id, teaser_id, city_id, country_code))
    end
end

-- Увеличивает счетчики для расчета веса
function M.incrScore(self, teasers_ids, key, incrBy)
    incrBy = incrBy or 1
    local tmp, teaser_id
    for tmp, teaser_id in pairs(teasers_ids) do
        self.sp:redis():hincrby(self.sp:getKey('teaser_score', teaser_id), key, incrBy)
    end
end

-- Возвращает данные о тизере
function M.getByEncrypted(self, encrypted)
    local result  = self.sp:redis():hmget(self.sp:getKey('teaser_encrypted', encrypted), "id", "url", "campaign_id")
    if result[1] ~= null and result[2] ~= null and result[3] ~= null then
        return unpack(result)
    end

    -- ngx.log(ngx.CRIT, "Cant find encrypted teaser "..tostring(encrypted))
end

return M