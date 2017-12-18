local M = {}

local function mysplit(inputstr, limit)
    local t={} ; i=1
    for str in string.gmatch(inputstr, "[^%.]+") do
        if i > limit then
            if t["rest"] ~= nil then
                if str ~= "" then
                    t["rest"] = t["rest"] .. "." .. str
                end
            else
                t["rest"] = str
            end
        else
            t[i] = str
            i = i + 1
        end
    end
    return t
end

-- парсит ссылку перехода
function M.parseLink(self, args)
    if args then
        for link, val in pairs(args) do
            local result = mysplit(link, 3)
            if result[3] ~= nil then
                result[2] = result[3]
            end
            return {eid = result[1], platform_eid = result[2], rest = result["rest"]}
        end
    end
end

-- добавляет необходимые параметры в url
function M.urlParameters(self, url, params)
    local args = ""
    for key,value in pairs(params) do
        args = args .. "&" .. key .. "=" .. value
    end

    return url:gsub("{args}", args)
end

-- Делает редирект на целевой урл, реферер используется tt.ttarget.ru
function M.redirect(self, url)
    local html = "<html><body><script type=\"text/javascript\">window.location.href='%s';</script></body></html>"
    ngx.say(string.format(html, url))
end

return M