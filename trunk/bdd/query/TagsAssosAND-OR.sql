SELECT 
COUNT(DISTINCT td.doc_id) nbDoc
-- , COUNT(DISTINCT t.tag_id)
 , t.tag_id, t.code
-- ,t1.code, t1.tag_id
-- ,t2.code, t2.tag_id
FROM flux_tag t 
 INNER JOIN flux_tagdoc td ON t.tag_id = td.tag_id 
 
 INNER JOIN flux_tagdoc td1 ON td1.doc_id = td.doc_id
INNER JOIN flux_tag t1 ON t1.tag_id = td1.tag_id 
 AND t1.code LIKE '%intelligence%'

 INNER JOIN flux_tagdoc td2 ON td2.doc_id = td.doc_id
 INNER JOIN flux_tag t2 ON t2.tag_id = td2.tag_id
 AND t2.code LIKE '%collective%'

-- WHERE t1.code LIKE '%intelligence%' AND t2.code LIKE '%collective%' 

 GROUP BY td.tag_id
-- HAVING t1.code LIKE '%intelligence%' OR t2.code LIKE '%collective%' 
ORDER BY nbDoc DESC