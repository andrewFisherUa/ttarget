--[[
    Скрипт фиксирующий действия типа CLICK
]]--

local sp = require "provider"

local track = sp:track():getByCode(ngx.var.arg_track)

if track.campaign_id and (track.teaser_id or track.offer_user_id) then
    local action_id = sp:action():getByEId(ngx.var.arg_id, track.campaign_id)
    if
        action_id and
        not sp:track():isActionTimingOut(ngx.var.arg_track, ngx.var.arg_id) and
        sp:track():setActionTimeOut(ngx.var.arg_track, ngx.var.arg_id)
    then
        if track.platform_id and track.teaser_id then
            -- teaser
            sp:resque():createJob('ActionsJob', {
                action_id    = action_id,
                encrypted_id = ngx.var.arg_id,
                timestamp    = os.time(),
                campaign_id  = track.campaign_id,
                teaser_id    = track.teaser_id,
                offer_id     = track.offer_id,
                platform_id  = track.platform_id,
                city_id      = ngx.var.arg_remote_addr and ngx.var.city_id_from_arg or ngx.var.city_id,
                country_code = ngx.var.arg_remote_addr and ngx.var.country_code_from_arg or ngx.var.country_code,
                track_id     = ngx.var.arg_track,
                ip           = ngx.var.arg_remote_addr and ngx.var.arg_remote_addr or ngx.var.remote_addr,
            })
        else
            -- offer
            sp:resque():createJob('OffersActionsJob', {
                offer_user_id = track.offer_user_id,
                city_id       = ngx.var.arg_remote_addr and ngx.var.city_id_from_arg or ngx.var.city_id,
                country_code  = ngx.var.arg_remote_addr and ngx.var.country_code_from_arg or ngx.var.country_code,
                timestamp     = os.time(),
                track_id      = ngx.var.arg_track,
                ip            = ngx.var.arg_remote_addr and ngx.var.arg_remote_addr or ngx.var.remote_addr
            })
        end
    end
end

sp:close()
