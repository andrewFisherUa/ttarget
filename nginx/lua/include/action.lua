local M = {}

local null = ngx.null

function M.getEIdsByCampaignId(self, campaign_id, action_eid)
    if
        action_eid ~= nil and
        self.sp:redis():sismember(self.sp:getKey("campaign_actions", campaign_id), action_eid) == 1
    then
        return {action_eid }
    else
        local action_ids = self.sp:redis():smembers(self.sp:getKey("campaign_actions", campaign_id))
        if table.getn(action_ids) > 0 then
            return action_ids
        end
    end

--    ngx.log(ngx.CRIT, "Cant get ids by campaign id "..tostring(campaign_id))
    return nil
end

function M.getAllByEIds(self, action_eids)
    local result = {}
    for i = 1, #action_eids do
        result[i] = { self:getByEId(action_eids[i]) }
    end

    return result
end

function M.getByEId(self, action_eid, campaign_id)
    local result = self.sp:redis():hmget(
        self.sp:getKey("action", action_eid),
        'id',
        'target_type',
        'target',
        'target_match_type',
        'campaign_id'
    )

    if campaign_id == nil or campaign_id == result[5] then
        if result[1] ~= null and result[2] ~= null and result[3] ~= null and result[4] ~= null then
            return unpack(result)
        else
            ngx.log(ngx.CRIT, "Inconsistent redis data for action: "..tostring(action_eid))
        end
    else
        ngx.log(ngx.CRIT, "Requested action campaign_id does not match: "..tostring(campaign_id)..' ~= '..tostring(result[5]))
    end

    return nil
end

return M