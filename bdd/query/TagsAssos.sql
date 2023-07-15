-- SELECT SUM(nbDoc), COUNT(DISTINCT tag_id)
-- from (

select
 t1.tag_id, t1.code, t1.desc
, SUM(td1.poids) poids1
, COUNT(DISTINCT(td.doc_id)) nbDoc
-- , COUNT(DISTINCT(dP.doc_id)), dP.tronc
FROM flux_tag t
INNER JOIN flux_tagdoc td ON td.tag_id = t.tag_id
INNER JOIN flux_tagdoc td1 ON td1.doc_id = td.doc_id
INNER JOIN flux_tag t1 ON t1.tag_id = td1.tag_id
INNER JOIN flux_doc d ON d.doc_id = td.doc_id  
-- LEFT JOIN flux_doc dP ON dP.doc_id = d.tronc
-- WHERE t.code LIKE '%media%' OR t.code LIKE '%information%' OR t.code LIKE '%communication%' OR t.code LIKE '%STIC%' OR t.code LIKE '%TIC%' 
WHERE t1.desc = "domaine" AND (d.note = 'SIC' OR d.note = 'TIC' )
GROUP BY t1.tag_id
ORDER BY t1.code

-- ) nb
