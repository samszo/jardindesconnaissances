SELECT 
    te.tag_id idE,
    te.code titreE,
    te.niveau-t.niveau+1 niveauE,
    rS.rapport_id sid,
    rS.monade_id sm,
    rS.dst_id sdid,
    rS.dst_obj sdo,
    rS.pre_id spid,
    rS.pre_obj spo,
    rD.rapport_id did,
    rD.monade_id dm,
    rD.src_id dsid,
    rD.src_obj dso,
    rD.pre_id dpid,
    rD.pre_obj dpo,
    rP.rapport_id pid,
    rP.monade_id pm,
    rP.dst_id pdid,
    rP.dst_obj pdo,
    rP.src_id psid,
    rP.src_obj pso    
FROM
    flux_tag t
        INNER JOIN
    flux_tag te ON te.lft BETWEEN t.lft AND t.rgt
        LEFT JOIN
    flux_rapport rS ON rS.src_id = te.tag_id
        AND rS.src_obj = 'tag'
        LEFT JOIN
    flux_rapport rD ON rD.dst_id = te.tag_id
        AND rD.dst_obj = 'tag'
        LEFT JOIN
    flux_rapport rP ON rP.pre_id = te.tag_id
        AND rP.pre_obj = 'tag'
WHERE
    t.tag_id = 524
-- group by de.niveau
ORDER BY te.lft