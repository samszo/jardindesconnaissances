SELECT 
	tT.ns lang, tT.code trad, tT.uri, tT.tag_id,
    t.code
    ,d.titre, d.url
FROM flux_tag t
        INNER JOIN
    flux_tag tT ON tT.parent = t.tag_id
		LEFT JOIN
	flux_rapport r ON r.dst_id = tT.tag_id AND r.dst_obj = "tag" AND r.pre_id=t.tag_id AND r.pre_obj="tag" AND r.src_obj = "doc"
    LEFT JOIN
    flux_doc d ON d.doc_id = r.src_id
	    					
-- WHERE t.tag_id = 3
WHERE t.code = "AmplitudeInterquartile"
ORDER BY d.url, tT.uri


