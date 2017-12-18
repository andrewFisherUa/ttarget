local M = {}

-- Добавляет задание в очередь
function M.createJob(self, class, params)
    local job = {}

    job['id'] = ngx.md5(os.clock())
    job['class'] = class
    job['args']  = {{}}
    job['args'][1] = params

    self.sp:redis():rpush(self.sp:getKey("queue_stat"), self.sp:cjson().encode(job))
end

return M

