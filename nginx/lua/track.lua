--[[
    Скрипт фиксирующий действия типа URL и возвращающий список целей типа CLICK
]]--

local sp = require "provider"

-- Проверяет соответсвует ли url действия рефереру
local function isUrlMatched(referer, url, match_type)
    if match_type == 'contain' and referer:find(url, 1, true) ~= nil then
        return true
    elseif match_type == 'match' and referer == url then
        return true
    elseif match_type == 'begin' and referer:sub(1, url:len()) == url then
        return true
    elseif match_type == 'regexp' then
        local regexp, options = url:match('^/(.+)/([^/]*)$')
        local match, err = ngx.re.match(referer, regexp, options)
        if match then
            return true
        end
    end
    return false
end

-- обработка действий
local function processActions(action_eids, referer, track_code, track)
    if action_eids == nil then
        return nil
    end
    results = sp:action():getAllByEIds(action_eids)

    local domTargets = {}
    local hasTargets = false

    -- проверям цели url и заполняем список целей click
    for i = 1, #results do
        local action_id, target_type, target, match_type = unpack(results[i])
        if target_type == 'url' then
            if isUrlMatched(referer, target, match_type) then
                if not sp:track():isActionTimingOut(track_code, action_eids[i]) and sp:track():setActionTimeOut(track_code, action_eids[i]) then
                    if track.teaser_id then
                        sp:resque():createJob('ActionsJob', {
                            action_id    = action_id,
                            encrypted_id = action_eids[i],
                            timestamp    = os.time(),
                            campaign_id  = track.campaign_id,
                            teaser_id    = track.teaser_id,
                            platform_id  = track.platform_id,
                            offer_id     = track.offer_id,
                            city_id      = ngx.var.city_id,
                            country_code = ngx.var.country_code,
                            track_id     = ngx.var.arg_track,
                            ip           = ngx.var.remote_addr
                        })
                    else
                        -- offer
                        sp:resque():createJob('OffersActionsJob', {
                            offer_user_id = track.offer_user_id,
                            city_id       = ngx.var.city_id,
                            country_code  = ngx.var.country_code,
                            track_id      = ngx.var.arg_track,
                            ip            = ngx.var.remote_addr,
                            timestamp     = os.time(),
                            ip            = ngx.var.remote_addr,
                            track_id      = ngx.var.arg_track
                        })
                    end
                end
            end
        else
            hasTargets = true
            domTargets[action_eids[i]] = target
        end
    end

    if hasTargets then
        -- возвращаем список целей click
        ngx.say('ttargetCPA.targets = ' .. sp:cjson().encode(domTargets) .. ';')
    end
end

local function processBounce(track)
    if track.bounce_check ~= nil and track.teaser_id ~= nil then
        local next_check_in = track.bounce_check - os.time()
        if next_check_in < 1 then
            sp:redis():hdel(sp:getKey('track', ngx.var.arg_track), "bounce_check")
            sp:resque():createJob('ClicksJob', {
                platform_id = track.platform_id,
                teaser_id = track.teaser_id,
                remote_addr = ngx.var.remote_addr,
                timestamp = os.time(),
                city_id = ngx.var.city_id,
                country_code = ngx.var.country_code,
                bc = 1
            })
        else
            ngx.say("ttargetCPA.bc = " .. next_check_in .. ";");
        end
    end
end

local track  = sp:track():getByCode(ngx.var.arg_track)
if track.campaign_id then
    local action_eids = sp:action():getEIdsByCampaignId(track.campaign_id, track.action_eid)
    processActions(action_eids, ngx.var.http_referer, ngx.var.arg_track, track)

    processBounce(track)

    local track_js_compiled = sp:campaign():getTrackJs(track.campaign_id)
    if(track_js_compiled ~= nil) then
      ngx.say(track_js_compiled);
    end
end
