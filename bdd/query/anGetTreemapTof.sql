SELECT 
    d.doc_id,
    d.titre,
    de.niveau + 1 - d.niveau niv,
    de.type,
    de.titre,
    de.doc_id,
    COUNT(DISTINCT dpe.doc_id) nbE,
    COUNT(DISTINCT dt.doc_id) nbTof
FROM
    flux_doc d
        INNER JOIN
    flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
        LEFT JOIN
    flux_doc dpe ON dpe.parent = de.doc_id
        LEFT JOIN
    flux_doc dt ON dt.parent = de.doc_id AND dt.type = 1
WHERE
    d.doc_id = 4185 and de.type is null 
GROUP BY niv , de.type, de.titre, de.doc_id
ORDER BY de.doc_id
