--[[
    Скрипт фиксирующий показы
]]--

local sp = require "provider"

-- Возвращает идентификаторы тизеров, которые будут показаны
local function parseTeasersIds()
    local args = ngx.req.get_uri_args()
    if args then
        for key, val in pairs(args) do
            if key == "id" then
                if type(val) ~= "table" then
                    val = {val}
                end
                return val;
            end
        end
    end

    return nil
end

-- Основная программа
local platform_id   = sp:platform():getIdByHost(sp:platform():getHostByReferer(ngx.var.http_referer))
local teasers_ids   = parseTeasersIds()

if not sp:isPilferer() and platform_id and teasers_ids then
    
    sp:redis():init_pipeline()
    sp:teaser():incrShows(platform_id, teasers_ids, ngx.var.city_id, ngx.var.country_code)
    sp:teaser():incrScore(teasers_ids, 'shows', 1)
    
    local ok, err = sp:redis():commit_pipeline()
    if not ok then
        ngx.log(ngx.CRIT, "Cant commit pipeline on show: "..err)
    end
end

sp:close()