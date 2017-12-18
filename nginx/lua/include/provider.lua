local M = {
    redis_keys = {
        teaser_html             = "ttarget:teasers:%u:html",
        teaser_encrypted        = "ttarget:teasers:link:%s",
        offer                   = "ttarget:offers:%s",
        offer_countries         = "ttarget:offers:%s:countries",
        offer_cities            = "ttarget:offers:%s:cities",
        offer_user_encrypted    = "ttarget:offers_users:%s",
        -- hash с данными для расчета веса тизера
        teaser_score            = "ttarget:teasers:%u:score",
        campaign                = "ttarget:campaigns:%u",
        campaign_actions        = "ttarget:campaigns:%u:actions",
        action                  = "ttarget:actions:%s",
        track                   = "ttarget:tracks:%u",
        track_sequence          = "ttarget:tracks:sequence",
        ip_log                  = "ttarget:ip:%s",
        queue_stat              = "ttarget:queue:stat",
        track_action_timeout    = "ttarget:tracks:%u:actions:%s:timeout",
        platforms_hosts         = "ttarget:platforms:hosts",
        platforms_encrypted     = "ttarget:platforms:encrypted",
        platforms_requests      = "ttarget:platforms_requests",
        rotation_version        = "ttarget:version",
        shows_counter           = "ttarget:shows:%u:%u:%u:%s",
        short_link              = "ttarget:short_link:%s",
--        user_session_geo        = "ttarget:user_session:geo",
--        user_session_teaser     = "ttarget:user_session:teaser",
        session_pages           = "ttarget:session:pages",
        pages_match_domain      = "ttarget:pages:%s"
    },
    
    -- время таймаута для трекинга действий, при котором повторно выполненые действия засчитываться не будут
    track_action_timeout = 43200,
    pages_match_sha = '73b8935d18ce997025d842c65dea1ec4025c3e07',
    yandex_rtb_secret = '0a6485894a688d5e8655e47d95bad4ac',
    yandex_cookie_match_url = 'http://an.yandex.ru/setud/goldfish/',
    pixel_url = 'http://tt.ttarget.ru/pixel.gif',
    external_teasers_url = 'http://ttarget.lan/site/ext',
    stop_page = 'http://ttarget.ru/news'
}

local null = ngx.null



-- форматирует ключ redis
function M.getKey(self, key, ...)
    local arg = {...}
    return string.format(self.redis_keys[key], unpack(arg))
end

function M.cjson(self)
    local cjson = require "cjson"
    return cjson
end

-- Возвращает версию алгоритма используемого для ротации тизеров
function M.getVersion(self, redis)
    local version = tonumber(ngx.var.arg_r) or self:redis():get(self:getKey('rotation_version'))
    if version ~= 0 and version ~= 1 and version ~= 2 then
        version = 1
    end
    return tostring(version)
end

function M.isPilferer(self, ip)
    ip = ip or ngx.var.remote_addr
    return 1 == self:redis():hget(self:getKey('ip_log', ip), "is_pilferer")
end

function M.inspect(self, ...)
    local args = {...}
    local inspect = require "inspect"
    return inspect(args)
end

function M.rToTable(self, input, keys, required)
    required = required == nil and false or required
    local result = {}
    for i,k in pairs(keys) do
        if input[i] ~= nil and input[i] ~= null then
            result[k] = input[i]
        elseif required then
            ngx.log(ngx.CRIT, "Cant convert result to hash."
                .." Keys: "..self:inspect(keys)..
                ", Input: "..self:inspect(input)
            )
            ngx.exit(ngx.HTTP_INTERNAL_SERVER_ERROR)
        end
    end
    return result
end

-- services

function M.track(self)
    local track = require "track"
    track.sp = self
    return track
end

function M.action(self)
    local action = require "action"
    action.sp = self
    return action
end

function M.resque(self)
    local resque = require "resque"
    resque.sp = self
    return resque
end

function M.platform(self)
    local platform = require "platform"
    platform.sp = self
    return platform
end

function M.teaser(self)
    local teaser = require "teaser"
    teaser.sp = self
    return teaser
end

function M.campaign(self)
    local campaign = require "campaign"
    campaign.sp = self
    return campaign
end

function M.offer(self)
    local offer = require "offer"
    offer.sp = self
    return offer
end

function M.url(self)
    local url = require "url"
    url.sp = self
    return url
end

function M.redis(self)
    if ngx.ctx._redis_connection == nil then
        local redis = require "resty.redis"
        local red = redis:new()

        red:set_timeout(1000) -- 1 sec

        local ok, err = red:connect("unix:/var/run/redis/redis.sock")
        if not ok then
            ngx.exit(ngx.HTTP_INTERNAL_SERVER_ERROR)
        end

        ngx.ctx._redis_connection = red
    end

    return ngx.ctx._redis_connection
end

function M.cookie(self)
    local ck = require "cookie"
    if ngx.ctx._cookie == nil then
        local cookie, err = ck:new()
        if not cookie then
            ngx.log(ngx.CRIT, err)
        end
        ngx.ctx._cookie = cookie
    end

    return ngx.ctx._cookie
end

function M.session(self)
    local session = require "session"
    session.sp = self
    return session
end

-- Закрывает соединение и добавляет в пул соединений на 30 сек
function M.close(self)
    if ngx.ctx._redis_connection ~= nil then
        ok, err = ngx.ctx._redis_connection:set_keepalive(30000, 500)
        if not ok then
            ngx.log(ngx.CRIT, "Cant return redis connection to pool: "..err)
            ngx.exit(0)
        end
    end
end

return M