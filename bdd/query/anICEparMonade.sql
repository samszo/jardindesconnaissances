SELECT 
    m.monade_id idE,
    m.titre titreE,
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
    flux_monade m
        LEFT JOIN
    flux_rapport rS ON (rS.src_id = m.monade_id
        AND rS.src_obj = 'monade') OR rS.monade_id = m.monade_id
        LEFT JOIN
    flux_rapport rD ON (rD.dst_id = m.monade_id
        AND rD.dst_obj = 'monade') OR rS.monade_id = m.monade_id
        LEFT JOIN
    flux_rapport rP ON (rP.pre_id = m.monade_id
        AND rP.pre_obj = 'monade') OR rS.monade_id = m.monade_id
WHERE
    m.monade_id = 1
ORDER BY m.monade_id