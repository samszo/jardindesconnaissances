SELECT count(*) nb, r.dst_id, t.code
-- , d.parent, d.titre
-- , dp.titre
 , dpp.titre
FROM flux_rapport r 
 INNER JOIN flux_tag t ON t.tag_id = r.dst_id
 INNER JOIN flux_rapport rDoc ON rDoc.rapport_id = r.src_id 
 INNER JOIN flux_doc d ON d.doc_id = rDoc.src_id
 INNER JOIN flux_doc dp ON dp.doc_id = d.parent
 INNER JOIN flux_doc dpp ON dpp.doc_id = dp.parent
WHERE r.dst_obj = 'tag' AND r.src_obj = 'rapport' 
GROUP BY r.dst_id
-- , d.parent
 , dpp.doc_id
ORDER BY nb DESC