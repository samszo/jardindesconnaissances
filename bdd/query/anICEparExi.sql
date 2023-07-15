SELECT 
    ee.exi_id idE,
    CONCAT(ee.prenom, ' ', ee.nom) titreE,
    ee.niveau - e.niveau + 1 niveauE,
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
    flux_exi e
        INNER JOIN
    flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
        LEFT JOIN
    flux_rapport rS ON rS.src_id = ee.exi_id
        AND rS.src_obj = 'exi'
        LEFT JOIN
    flux_rapport rD ON rD.dst_id = ee.exi_id
        AND rD.dst_obj = 'exi'
        LEFT JOIN
    flux_rapport rP ON rP.pre_id = ee.exi_id
        AND rP.pre_obj = 'exi'
WHERE
    e.exi_id = 16
ORDER BY ee.lft