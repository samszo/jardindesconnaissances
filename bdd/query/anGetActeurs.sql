SELECT 
    COUNT(DISTINCT dp.doc_id) nbPar,
    SUM(dp.niveau) nivPar,
    COUNT(DISTINCT de.doc_id) nbTot,
    SUM(de.niveau) nivTot,
    e.exi_id,
    e.nom,
    e.prenom
FROM
    flux_doc d
        INNER JOIN
    flux_doc dp ON d.lft BETWEEN dp.lft AND dp.rgt
        AND dp.niveau = (d.niveau - 1)
        INNER JOIN
    flux_doc de ON de.lft BETWEEN dp.lft AND dp.rgt
        LEFT JOIN
    flux_rapport r ON r.src_id = de.doc_id
        AND r.src_obj = 'doc'
        AND r.dst_obj = 'exi'
        LEFT JOIN
    flux_exi e ON e.exi_id = r.dst_id
WHERE
    d.doc_id = 5436 AND e.exi_id IS NOT NULL
GROUP BY e.exi_id
ORDER BY e.nom , e.prenom