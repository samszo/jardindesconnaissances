SELECT count(distinct d.doc_id), count(distinct dp.doc_id)
FROM flux_doc d
INNER JOIN flux_doc dP ON dp.doc_id = d.tronc
WHERE d.type = "note"