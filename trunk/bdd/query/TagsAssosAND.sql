 SELECT filtre.tag_id, filtre.code, COUNT(filtre.doc_id) nbDoc
 FROM (
SELECT d.doc_id
 , t.tag_id, t.code
-- ,t1.code, t1.tag_id
-- ,t2.code, t2.tag_id
FROM flux_doc d
 INNER JOIN flux_tagdoc td ON td.doc_id = d.doc_id
 INNER JOIN flux_tag t ON t.tag_id = td.tag_id 
-- INNER JOIN flux_tagdoc td1 ON td1.tag_id = td.tag_id
 INNER JOIN flux_tagdoc td1 ON td1.doc_id = td.doc_id
INNER JOIN flux_tag t1 ON t1.tag_id = td1.tag_id 
 INNER JOIN flux_tagdoc td2 ON td2.doc_id = td.doc_id
-- INNER JOIN flux_tagdoc td2 ON td2.tag_id = td.tag_id
 INNER JOIN flux_tag t2 ON t2.tag_id = td2.tag_id   
WHERE t1.code LIKE '%intelligence%' AND t2.code LIKE '%collective%' 
GROUP BY d.doc_id
 ) filtre
 GROUP BY filtre.tag_id
-- ORDER BY nbDoc DESC