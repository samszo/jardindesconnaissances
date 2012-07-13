SELECT SUM(nbDoc), COUNT(DISTINCT tag_id)
from (
select t1.tag_id, t1.code, SUM(td1.poids) poids, COUNT(DISTINCT(td.doc_id)) nbDoc
, COUNT(DISTINCT(dP.doc_id)), dP.tronc
FROM flux_tag t
INNER JOIN flux_tagdoc td ON td.tag_id = t.tag_id
INNER JOIN flux_tagdoc td1 ON td1.doc_id = td.doc_id
INNER JOIN flux_tag t1 ON t1.tag_id = td1.tag_id
INNER JOIN flux_doc d ON d.doc_id = td.doc_id  
LEFT JOIN flux_doc dP ON dP.doc_id = d.tronc
WHERE t.code LIKE '%intelligence%' OR t.code LIKE '%collective%' 
GROUP BY t1.tag_id
ORDER BY t1.tag_id DESC
) nb
