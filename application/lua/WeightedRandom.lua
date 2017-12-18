local lc = redis.call('SMEMBERS', KEYS[1])
local count = tonumber(ARGV[1])
if #lc <= count then
    return lc
end
local total = 0
local score = {}
for i = 1, #lc do
    score[i] = tonumber(redis.call('ZSCORE', KEYS[2], lc[i]))
    if score[i] < 1 then score[i] = 1 end
    total = total + score[i]
end
local results = {}
math.randomseed(ARGV[2])
while #results < count do
    local r = math.random(1, total)
    for i=1, #score do
        if r <= score[i] then
            table.insert(results, lc[i])
            total = total - score[i]
            table.remove(score, i)
            table.remove(lc, i)
            break
        end
        r = r - score[i]
    end
end
return results