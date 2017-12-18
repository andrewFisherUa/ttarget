--[[
    Скрипт фиксирующий переходы
]]--

local sp = require "provider"

-- Основная программа

local args = sp:url():parseLink(ngx.req.get_uri_args(1))

local platform_id
if args.platform_eid ~= nil then
    platform_id = sp:platform():getEIdOrId(args.platform_eid)
else
    platform_id = sp:platform():getIdByHost(sp:platform():getHostByReferer(ngx.var.http_referer))
end

local teaser_id, url, campaign_id = sp:teaser():getByEncrypted(args.eid)

if campaign_id and teaser_id and sp:campaign():isActive(campaign_id) then
    local track_code
    if platform_id and not sp:isPilferer() then
        track_code = sp:track():createTrackCode({
            campaign_id = campaign_id,
            teaser_id = teaser_id,
            platform_id = platform_id
        })
        
        if platform_id and not sp:isPilferer() then
            sp:resque():createJob('ClicksJob', {
                platform_id = platform_id,
                teaser_id = teaser_id,
                remote_addr = ngx.var.remote_addr,
                timestamp = os.time(),
                city_id = ngx.var.city_id,
                country_code = ngx.var.country_code,
                track_id = track_code
            })

            --sp:session():teaser(sp:session():start(), teaser_id)
        end
    end

    sp:close()

    if teaser_id == "1" and args.rest ~= nil then
        sp:url():redirect(args.rest)
    else
        sp:url():redirect(sp:url():urlParameters(url, {utm_campaign = platform_id, track = track_code}))
    end
else
    sp:url():redirect(sp.stop_page)
end