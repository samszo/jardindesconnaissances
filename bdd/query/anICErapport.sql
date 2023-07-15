SELECT 
    CONCAT(r.src_obj,
            '-',
            r.dst_obj,
            '-',
            r.pre_obj) obj,
    COUNT(DISTINCT r.rapport_id) nbRapport,
    COUNT(DISTINCT r.src_id) nbSrc,
    COUNT(DISTINCT r.dst_id) nbDst,
    COUNT(DISTINCT r.pre_id) nbPre,
    COUNT(DISTINCT r.rapport_id)+COUNT(DISTINCT r.src_id)+COUNT(DISTINCT r.dst_id)+COUNT(DISTINCT r.pre_id) complexite
FROM
    flux_rapport r
GROUP BY CONCAT(r.src_obj,
        '-',
        r.dst_obj,
        '-',
        r.pre_obj)