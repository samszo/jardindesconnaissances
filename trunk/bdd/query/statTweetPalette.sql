-- SELECT COUNT(f.doc_id) nbClic, SUM(f.poids) poids, COUNT(DISTINCT tronc) nbTronc, data 
-- SELECT f.* 
SELECT 
d.doc_id, d.poids, d.data
-- COUNT(d.doc_id) nbClic, SUM(d.poids) sumPoids
-- , GROUP_CONCAT(DISTINCT d.data)
-- , SUBSTRING(d.tronc,LENGTH('1218_')+1) idUrl
, dU.doc_id idU, dU.url urlU 
-- ,SUBSTRING(dU.url,INSTR(dU.url, 'id=')+3) idTof
, dT.doc_id idT, dt.titre, dt.url urlT
, u.login, u.uti_id
FROM `flux_doc` AS d 
INNER JOIN flux_doc dU ON dU.doc_id = SUBSTRING(d.tronc,LENGTH('1218_')+1)
INNER JOIN flux_doc dT ON dT.doc_id = SUBSTRING(dU.url,INSTR(dU.url, 'id=')+3)
INNER JOIN flux_utidoc ud ON ud.doc_id = dU.doc_id
INNER JOIN flux_uti u ON u.uti_id = ud.uti_id
WHERE (d.tronc LIKE '%1218_%')
-- GROUP BY d.data
-- GROUP BY d.tronc
-- ORDER BY sumPoids
