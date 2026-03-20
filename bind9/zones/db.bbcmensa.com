; ============================================================
;  MENSA Infrastructure — DNS Forward Zone File
;  Domain : bbcmensa.com
; ============================================================

$TTL 86400      ; Default TTL = 24 hours (in seconds)

; ── Start of Authority (SOA) ────────────────────────────────
@   IN  SOA     ns1.bbcmensa.com. admin.bbcmensa.com. (
                2025062002  ; Serial   — YYYYMMDDNN
                3600        ; Refresh  
                900         ; Retry    
                604800      ; Expire   
                86400       ; Minimum TTL
)

; ── Name Server Records ─────────────────────────────────────
@               IN  NS      ns1.bbcmensa.com.

; ── A Records (Hostname → IPv4) ─────────────────────────────
@               IN  A       172.28.0.10     
ns1             IN  A       172.28.0.53     
www             IN  A       172.28.0.10     
db              IN  A       172.28.0.20     
dns             IN  A       172.28.0.53     

; ── CNAME Records (Aliases) ─────────────────────────────────
mail            IN  CNAME   www.bbcmensa.com.    
api             IN  CNAME   www.bbcmensa.com.    
team            IN  CNAME   www.bbcmensa.com.    

; ── MX Record (Mail Exchange) ───────────────────────────────
@               IN  MX  10  mail.bbcmensa.com.

; ── TXT Records ─────────────────────────────────────────────
@               IN  TXT     "v=spf1 ip4:172.28.0.10 ~all"
@               IN  TXT     "MENSA Tech Agency"