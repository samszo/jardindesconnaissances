SELECT 
    d.doc_id,
    rt.valeur,
    t.tag_id recid, t.code, t.desc, tP.code type,
    COUNT(rT.src_id) nbDoc,
    COUNT(rT.dst_id) nbTag,
--    tL.ns lang, tL.code langue,
	tT.code trad, tT.ns, tT.desc, tT.uri
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
    --    INNER JOIN
    -- flux_rapport rTt ON rTt.pre_id = rT.rapport_id
    --    AND rTt.src_obj = 'tag'
    --    AND rTt.dst_obj = 'tag'
    --    AND rTt.pre_obj = 'rapport'
   --     INNER JOIN
   -- flux_tag tL ON tL.tag_id = rTt.valeur
        INNER JOIN
    flux_tag tT ON tT.parent = t.tag_id
-- where tL.ns = 'fa'
GROUP BY tT.tag_id, t.tag_id
ORDER BY t.code, tT.ns; -- nbDoc desc;