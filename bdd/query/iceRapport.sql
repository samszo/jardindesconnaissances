/* SELECT 
    SUM(nbRapport),
    SUM(nbSrc),
    SUM(nbDst),
    SUM(nbPre),
    SUM(nbSrc) + SUM(nbDst) + SUM(nbPre) totalDim,
    SUM(nbRapport) + SUM(nbSrc) + SUM(nbDst) + SUM(nbPre) total
FROM
    (
    */
    SELECT 
        COUNT(*) nbRapport,
            COUNT(DISTINCT (src_id)) AS nbSrc,
            src_obj,
            COUNT(DISTINCT (dst_id)) AS nbDst,
            dst_obj,
            COUNT(DISTINCT (pre_id)) AS nbPre,
            pre_obj
    FROM
        flux_rapport
    WHERE
    /*
        (src_obj IN ('exi' , 'doc', 'tag', 'rapport')
            AND dst_obj IN ('exi' , 'doc', 'tag', 'rapport')
            AND pre_obj IN ('exi' , 'doc', 'tag', 'rapport'))
     (src_id IN (8 , 111, 502) AND src_obj='exi')
     OR
     (dst_id IN (8 , 111, 502) AND dst_obj='exi')
     OR
     (pre_id IN (8 , 111, 502) AND pre_obj='exi')
     */ 
     (src_id IN (7) AND src_obj='exi')
     OR
     (dst_id IN (7) AND dst_obj='exi')
     OR
     (pre_id IN (7) AND pre_obj='exi')
    GROUP BY src_obj , dst_obj , pre_obj
    ORDER BY src_obj , dst_obj , pre_obj
 --   ) detail