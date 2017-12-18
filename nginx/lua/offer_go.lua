--[[
    Скрипт фиксирующий переходы
]]--

local sp = require "provider"

-- Основная программа

local args = sp:url():parseLink(ngx.req.get_uri_args(1))
local offer_user = sp:offer():getOfferUserByEncrypted(args.eid)
local offer = sp:offer():getById(offer_user.offer_id)

if
    offer.campaign_id and
    sp:campaign():isActive(offer.campaign_id) and
    offer_user.offer_id and
    sp:offer():isActiveForGEO(offer_user.offer_id, ngx.var.country_code, ngx.var.city_id)
then
    local track_code
    if not sp:isPilferer() then
        track_code = sp:track():createTrackCode({
            campaign_id = offer.campaign_id,
            offer_user_id = offer_user.id,
            action_eid = offer.action_eid
        })

        sp:resque():createJob('OffersClicksJob', {
            offer_user_id = offer_user.id,
            remote_addr = ngx.var.remote_addr,
            timestamp = os.time(),
            city_id = ngx.var.city_id,
            country_code = ngx.var.country_code,
            track_id = track_code,
            referrer = ngx.var.http_referer,
        })
    end

    sp:close()
    sp:url():redirect(sp:url():urlParameters(offer.url, {track = track_code, track_exp = offer.cookie_expires}))
else
    sp:url():redirect(sp.stop_page)
end