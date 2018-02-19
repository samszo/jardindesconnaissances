SELECT 
    g.geo_id idE,
    g.adresse titreE,
    1 niveauE,
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
    flux_geo g
        LEFT JOIN
    flux_rapport rS ON rS.src_id = g.geo_id
        AND rS.src_obj = 'geo'
        LEFT JOIN
    flux_rapport rD ON rD.dst_id = g.geo_id
        AND rD.dst_obj = 'geo'
        LEFT JOIN
    flux_rapport rP ON rP.pre_id = g.geo_id
        AND rP.pre_obj = 'geo'
WHERE
    g.geo_id = 9
ORDER BY g.geo_id