SELECT d.doc_id
, d.url REGEXP "utm_content"
, SUBSTRING(d.url, INSTR(d.url, '/?utm_source=')+13, INSTR(d.url, '&')-INSTR(d.url, '/?utm_source=')-13) fluxSource
, SUBSTRING(d.url, INSTR(d.url, 'utm_medium=')+11, INSTR(d.url, '&utm_campaign')-INSTR(d.url, 'utm_medium')-11) fluxMedium
, SUBSTRING(d.url, INSTR(d.url, 'utm_campaign=')+13, INSTR(d.url, '&utm_content')-INSTR(d.url, 'utm_campaign')-13) fluxCampaign
, SUBSTRING(d.url, INSTR(d.url, 'utm_content=')+12) fluxContent
, d.url 
FROM flux_Doc d 
INNER JOIN flux_UtiDoc ud ON ud.doc_id = d.doc_id AND ud.uti_id  = 1
WHERE d.url LIKE "%/?utm_source=%"
ORDER BY d.doc_id 

