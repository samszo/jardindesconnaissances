SELECT 
    tL.ns lang, tL.code langue,
	tT.code trad
FROM flux_tag tT
        INNER JOIN
    flux_rapport rTt ON rTt.dst_id = tT.tag_id
		AND rTt.src_id = tT.parent
        AND rTt.src_obj = 'tag'
        AND rTt.dst_obj = 'tag'
        AND rTt.pre_obj = 'rapport'        
        INNER JOIN
    flux_tag tL ON tL.tag_id = rTt.valeur
-- where tL.ns = 'fa'
-- GROUP BY tL.tag_id, tT.tag_id
WHERE tT.parent = 64
ORDER BY tL.code; -- nbDoc desc;