SELECT 
    t.code
    ,d.titre, d.url
    , r.valeur
    , rdl.valeur
    , dl.titre, dl.url
FROM flux_tag t
        INNER JOIN
	flux_rapport r ON r.dst_id = t.tag_id AND r.dst_obj = "tag" AND r.dst_id=t.tag_id AND r.pre_obj="rapport" AND r.src_obj = "doc"
		INNER JOIN
	flux_doc d on d.doc_id = r.src_id
        INNER JOIN
	flux_rapport rdl ON rdl.pre_id = r.rapport_id AND rdl.pre_obj="rapport" AND rdl.dst_obj="doc" 
		INNER JOIN
	flux_doc dl on dl.doc_id = rdl.dst_id
WHERE t.code = "AmplitudeInterquartile"
