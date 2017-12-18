local resultId
local resultLen = -1
local done = false
local cursor = "0"
local argLen = ARGV[1]:len()
repeat
    local result = redis.call("HSCAN", KEYS[1], cursor)
    cursor = result[1];
    local f, s, next_key = pairs(result[2])
    local pageId,pagePath;
    while true do
        next_key, pageId = f(s, next_key)
        if next_key == nil then break end
        next_key, pagePath = f(s, next_key)
        local pagePathLen = pagePath:len()
        if pagePathLen <= argLen and pagePathLen > resultLen then
            if ARGV[1]:sub(1, pagePathLen) == pagePath then
                resultLen = pagePathLen
                resultId = pageId
            end
        end
    end
    if cursor == "0" then
        done = true;
    end
until done
return resultId

