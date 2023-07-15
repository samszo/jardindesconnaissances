SELECT 
	r.rapport_id, r.maj, r.niveau, r.valeur
    , t.tag_id, t.code
    , d.doc_id, d.titre, d.data
    , u.uti_id, u.login
    , a.acti_id, a.code
    -- , rrr.*
    , t1.tag_id, t1.code
    , d1.doc_id, d1.titre, d1.data
    , d2.doc_id, d2.titre, d2.data
FROM flux_rapport r
	INNER JOIN flux_tag t ON t.tag_id = r.src_id
	INNER JOIN flux_doc d ON d.doc_id = r.dst_id
	INNER JOIN flux_rapport rr ON rr.rapport_id = r.pre_id
	INNER JOIN flux_uti u ON u.uti_id = rr.pre_id
    INNER JOIN flux_acti a ON a.acti_id = rr.dst_id
    INNER JOIN flux_rapport rrr ON rrr.rapport_id = rr.src_id
	INNER JOIN flux_tag t1 ON t1.tag_id = rrr.pre_id
	INNER JOIN flux_doc d2 ON d2.doc_id = rrr.src_id
	INNER JOIN flux_doc d1 ON d1.doc_id = rrr.dst_id
	    
ORDER BY r.maj DESC 