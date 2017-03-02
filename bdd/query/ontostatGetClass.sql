SELECT 
	COUNT(t.code) nbClass,
    d.doc_id,
    rT.valeur, rT.rapport_id,
    t.tag_id recid, t.code, t.desc, tP.code type
FROM
    flux_doc d
        INNER JOIN
    flux_rapport r ON r.src_id = d.doc_id
        AND r.src_obj = 'doc'
        AND r.dst_obj = 'acti'
        INNER JOIN
    flux_rapport rT ON rT.pre_id = r.rapport_id
        AND rT.src_obj = 'doc'
        AND rT.dst_obj = 'tag'
        AND rT.pre_obj = 'rapport'
        INNER JOIN
    flux_tag t ON t.tag_id = rT.dst_id
        INNER JOIN
    flux_tag tP ON tP.tag_id = t.parent
WHERE rT.valeur = 'Class'
GROUP BY t.tag_id
ORDER BY t.code ;