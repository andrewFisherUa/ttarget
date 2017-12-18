local M = {}

local null = ngx.null

-- проверяет наличие действий для кампании
function M.isActionsExists(self, campaign_id, action_eid)
    if action_eid ~= nil then
        return self.sp:redis():sismember(self.sp:getKey('campaign_actions', campaign_id), action_eid) == 1
    else
        return self.sp:redis():exists(self.sp:getKey('campaign_actions', campaign_id)) == 1
    end

end

function M.isActive(self, campaign_id)
    local res = self.sp:redis():hmget(self.sp:getKey('campaign', campaign_id), "is_active", "date_end")
    res[2] = tonumber(res[2])
    if res[2] == nil then
        ngx.log(ngx.CRIT, "Cant get date_end for campaign "..tostring(campaign_id))
    elseif res[1] == '1' and res[2] > os.time() then
        return true
    end
    return false
end

function M.getTrackJs(self, campaign_id)
    local res = self.sp:redis():hget(self.sp:getKey('campaign', campaign_id), "track_js_compiled")
    if res ~= null and res ~= "" then
        return res
    end
    return nil;
end

return M