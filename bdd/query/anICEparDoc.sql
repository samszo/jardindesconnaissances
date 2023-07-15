SELECT 
/*
    d.doc_id,
    d.titre,
    d.niveau,
    */
    de.doc_id idE,
    de.titre titreE,
    de.niveau-d.niveau+1 niveauE,
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
    flux_doc d
        INNER JOIN
    flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
        LEFT JOIN
    flux_rapport rS ON rS.src_id = de.doc_id
        AND rS.src_obj = 'doc'
        LEFT JOIN
    flux_rapport rD ON rD.dst_id = de.doc_id
        AND rD.dst_obj = 'doc'
        LEFT JOIN
    flux_rapport rP ON rP.pre_id = de.doc_id
        AND rP.pre_obj = 'doc'
WHERE
    d.doc_id = 5978
ORDER BY de.lft
