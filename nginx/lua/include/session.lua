-- SESSION --
local md5 = require "md5"

local M = {
    cookieSessionId = '__tt1',
    cookieGeoInfo = '__tt2'
}

local function createSessionId()
    math.randomseed(ngx.now() * 1000)
    return md5.sumhexa(math.random() .. ngx.req.raw_header())
end

local function createSession(self)
    local uid = createSessionId()
    local ok, err = self.sp:cookie():set({
        key = M.cookieSessionId,
        value = uid,
        path = "/",
        httponly = true,
        max_age = 94608000
    })
    if not ok then
        ngx.log(ngx.ERR, 'Set session cookie:' .. err)
    end
    return uid
end

--local function getCurrentGeoInfo()
--    return ngx.var.country_code .. '-' .. ngx.var.city_id
--end

local function deleteOldCookies(self)
    local v, err = self.sp:cookie():get('_tt1');
    if v ~= nil then
        self.sp:cookie():set({
            key = '_tt1',
            value = '',
            path = "/",
            httponly = true,
            max_age = 0
        })
    end

    local v, err = self.sp:cookie():get('_tt2');
    if v ~= nil then
        self.sp:cookie():set({
            key = '_tt2',
            value = '',
            path = "/",
            httponly = true,
            max_age = 0
        })
    end
end

--local function processGeo(self, uid)
--    local geo, err = self.sp:cookie():get(M.cookieGeoInfo)
--    local newGeo = getCurrentGeoInfo()
--    if geo ~= newGeo then
--        self.sp:cookie():set({
--            key = M.cookieGeoInfo,
--            value = newGeo,
--            path = "/",
--            httponly = true,
--            max_age = 94608000
--        })
--        self.sp:redis():sadd(self.sp:getKey('user_session_geo'), uid .. '-' .. newGeo)
--    end
--end

local function processPages(self, uid, referer)
    local host, path = self.sp:platform():getHostByReferer(referer, true)
    if host then
        local pageId = self.sp:redis():evalsha(
            self.sp.pages_match_sha,
            1,
            self.sp:getKey('pages_match_domain', host),
            path
        )
        if pageId ~= ngx.null then
            self.sp:redis():hincrby(self.sp:getKey('session_pages'), uid .. '-' .. pageId, incrBy or 1)
        end

    end
end

function M.start(self)
    deleteOldCookies(self)
    local uid, err = self.sp:cookie():get(M.cookieSessionId)
    if uid == nil then
        uid = createSession(self)
    end
    --processGeo(self, uid)
    processPages(self, uid, ngx.var.http_referer or '')

    return uid
end

--function M.teaser(self, uid, teaserId, incrBy)
--    self.sp:redis():hincrby(self.sp:getKey('user_session_teaser'), uid .. '-' .. teaserId, incrBy or 1)
--end

return M
-- SESSION --