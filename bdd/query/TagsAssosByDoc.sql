SELECT td1.doc_id, GROUP_CONCAT(DISTINCT(td1.tag_id))
FROM flux_tag t
INNER JOIN flux_tagdoc td ON td.tag_id = t.tag_id
INNER JOIN flux_tagdoc td1 ON td1.doc_id = td.doc_id
INNER JOIN flux_doc d ON d.doc_id = td.doc_id 
WHERE t.code LIKE '%intelligence%' OR t.code LIKE '%collective%' 
GROUP BY td1.doc_id
ORDER BY td1.tag_id
