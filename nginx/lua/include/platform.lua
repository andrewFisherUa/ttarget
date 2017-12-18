local M = {}

local null = ngx.null

-- Возвращает хост площадки по рефереру
function M.getHostByReferer(self, referer, returnPath)
    returnPath = returnPath == nil and false or returnPath

    if not referer then
        return nil
    end
    local regex = "^(https?://)?(www\\.)?([a-z0-9-\\.]+)/?(.*)$"
    local match = ngx.re.match(referer:lower(), regex)

    if returnPath then
        return match[3], match[4]
    else
        return match[3]
    end
end

-- Получает идентификатор площадки из редис по хосту
function M.getIdByHost(self, host)
    if host then
        local platform_id, err = self.sp:redis():zscore(self.sp:getKey('platforms_hosts'), host)
        if platform_id ~= null then
            return platform_id
        end
        ngx.log(ngx.CRIT, "Cant find platform by host: "..tostring(host))
    end

    return nil
end

function M.getEIdOrId(self,  id)
    if id ~= nil then
        local encrypted_platform_id = self.sp:redis():hget(self.sp:getKey('platforms_encrypted'), id)
        if encrypted_platform_id ~= null then
            return encrypted_platform_id
        end

        ngx.log(ngx.CRIT, "Cant find eid or id: "..tostring(id))
    end

    return nil
end

function M.addRequestLog(self,  id)
    if id ~= nil then
        self.sp:redis():sadd(self.sp:getKey('platforms_requests'), id)
    end
end

return M