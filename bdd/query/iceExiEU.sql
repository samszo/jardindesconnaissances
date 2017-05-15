SELECT 
    rS.exiRootNom,
    rS.dst_obj,
    COUNT(DISTINCT rS.dst_id) rSnbDst,
    rS.pre_obj,
    COUNT(DISTINCT rS.pre_id) rSnbPre
FROM
    (SELECT 
        e.exi_id exiRootId, e.nom exiRootNom, rs.*
    FROM
        flux_exi e
    INNER JOIN flux_rapport rs ON rs.src_id = e.exi_id
        AND rs.src_obj = 'exi'
    WHERE
        e.exi_id IN (8 , 111, 502)) rS,
    (SELECT 
        e.exi_id exiRootId, e.nom exiRootNom, rs.*
    FROM
        flux_exi e
    INNER JOIN flux_rapport rs ON rs.dst_id = e.exi_id
        AND rs.dst_obj = 'exi'
    WHERE
        e.exi_id IN (8 , 111, 502)) rD
        
GROUP BY rS.src_obj, rS.dst_obj , rS.pre_obj