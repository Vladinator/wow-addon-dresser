local _, ns = ...

local locale = GetLocale()
local L = setmetatable({}, { __index = function(self, k) return "[" .. locale .. "] " .. k end })
ns.L = L

-- enGB/enUS
L.UNDRESS = "Undress"
L.UNDRESS_SHORT = "Und"
L.INSPECT = "Inspect"
L.INSPECT_SHORT = "Ins"
L.TARGET = "Target"
L.TARGET_SHORT = "Tar"
L.PLAYER = "Player"
L.PLAYER_SHORT = "Plr"
L.CUSTOM = "Custom"
L.CUSTOM_SHORT = "Cus"
