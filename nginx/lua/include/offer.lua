local M = {}

local null = ngx.null

-- Возвращает данные о оффере
function M.getOfferUserByEncrypted(self, encrypted)
    local keys = {"id", "offer_id" }
    local t = self.sp:rToTable(
        self.sp:redis():hmget(self.sp:getKey('offer_user_encrypted', encrypted), unpack(keys)),
        keys
    )

    return t
end

function M.isActiveForGEO(self, offer_id, country_code, city_id)
    if
        (
            self.sp:redis():exists(self.sp:getKey('offer_countries', offer_id)) == 0 and
            self.sp:redis():exists(self.sp:getKey('offer_cities', offer_id)) == 0
        ) or
        self.sp:redis():sismember(self.sp:getKey('offer_countries', offer_id), country_code) == 1 or
        self.sp:redis():sismember(self.sp:getKey('offer_cities', offer_id), city_id) == 1
    then
        return true
    end

    return false
end

function M.getById(self, offer_id)
    if not offer_id then
        return {}
    end

    local keys = {"campaign_id", "action_eid", "url", "cookie_expires" }
    local t = self.sp:rToTable(
        self.sp:redis():hmget(self.sp:getKey('offer', offer_id), unpack(keys)),
        keys
    )

    t.cookie_expires = tonumber(t.cookie_expires)
    if t.cookie_expires and t.cookie_expires < 1 then
        t.cookie_expires = nil
    end

    return t
end

return M