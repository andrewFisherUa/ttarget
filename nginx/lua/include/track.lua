local M = {}

local null = ngx.null

-- Провярет происходило ли действие для текущего track-номера в течении таймаута
function M.isActionTimingOut(self, track_code, action_id)
    local timeout = self.sp:redis():get(self.sp:getKey('track_action_timeout', track_code, action_id))
    if timeout ~= null and tonumber(timeout) > os.time() then
        return true
    end
    return false
end

-- Устанавливает таймаут на действие для текущего track-номера
function M.setActionTimeOut(self, track_code, action_id)
    local res = self.sp:redis():set(
        self.sp:getKey('track_action_timeout', track_code, action_id),
        os.time()+self.sp.track_action_timeout
    )
    return res == 'OK'
end

-- Возвращает время через которое нужно проверять отказ
local function getBounceCheck(sp, campaign_id)
    local bounce_check = sp:redis():hget(sp:getKey('campaign', campaign_id), "bounce_check")
    if bounce_check == null or bounce_check == "" then
        return nil
    end

    return tonumber(bounce_check)
end

-- Возвращяет идентификатор отслеживания, если это придусмотрено кампанией
function M.createTrackCode(self, track_info)
    if track_info.offer_user_id == nil then
        track_info.bounce_check = getBounceCheck(self.sp, track_info.campaign_id)
        if track_info.bounce_check ~= nil then
            track_info.bounce_check = os.time() + track_info.bounce_check
        end
    end

    if
        track_info.bounce_check or
        self.sp:campaign():isActionsExists(track_info.campaign_id, track_info.action_eid) or
        self.sp:campaign():getTrackJs(track_info.campaign_id) ~= nil
    then
        local track_code = self.sp:redis():incr(self.sp:getKey('track_sequence'))
        track_info.created_date = os.time();
        local res, err = self.sp:redis():hmset(self.sp:getKey('track', track_code), track_info)
        return track_code
    end
end

-- возвращает данные отслеживания по коду
function M.getByCode(self, track_code)
    if track_code then
        local keys = {"campaign_id", "platform_id", "teaser_id", "bounce_check", "offer_user_id", "action_eid" }
        local t = self.sp:rToTable(
            self.sp:redis():hmget(self.sp:getKey('track', track_code), unpack(keys)),
            keys
        )

        if t.bounce_check == "" then
            t.bounce_check = nil
        end

        if t.campaign_id then
            return t
        end
    end

    ngx.log(ngx.CRIT, "Cant get info by trac code "..tostring(track_code))
    return {}
end

return M