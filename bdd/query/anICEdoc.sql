/*
SELECT 
    SUM(ice.nb), SUM(ice.niv), SUM(ice.complexite)
FROM
    (
*/
    SELECT 
        COUNT(DISTINCT de.doc_id) nb,
            de.niveau + 1 - d.niveau niv,
            COUNT(DISTINCT de.doc_id) * (de.niveau + 1 - d.niveau) complexite
    FROM
        flux_doc d
    INNER JOIN flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
    WHERE d.doc_id IN (668,285,60) 
    GROUP BY d.niveau , de.niveau
    
--    ) ice