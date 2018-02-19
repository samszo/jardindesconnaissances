SELECT 
    COUNT(DISTINCT m.monade_id) nb,
    1 niv,
    COUNT(DISTINCT m.monade_id) * 1 complexite,
    CONCAT(r.src_obj,
            '-',
            r.dst_obj,
            '-',
            r.pre_obj) obj,
    COUNT(DISTINCT r.rapport_id) nbRapport,
    COUNT(DISTINCT r.src_id) nbSrc,
    COUNT(DISTINCT r.dst_id) nbDst,
    COUNT(DISTINCT r.pre_id) nbPre,
    COUNT(DISTINCT r.rapport_id) + COUNT(DISTINCT r.src_id) + COUNT(DISTINCT r.dst_id) + COUNT(DISTINCT r.pre_id) complexiteRapport
FROM
    flux_monade m
        INNER JOIN
    flux_rapport r ON r.monade_id = m.monade_id
GROUP BY m.monade_id , CONCAT(r.src_obj,
        '-',
        r.dst_obj,
        '-',
        r.pre_obj)
