SELECT docRootId, docRootTitre
,niveau, nbDocEnfNiv, niveau*nbDocEnfNiv iceNiv
FROM(
SELECT 
    d.doc_id docRootId,
    d.titre docRootTitre,
    d.niveau docRootNiveau,
    (de.niveau - d.niveau + 1) niveau,
    COUNT(DISTINCT de.doc_id) nbDocEnfNiv
FROM
    flux_doc d
        INNER JOIN
    flux_doc de ON de.lft BETWEEN d.lft AND d.rgt
 WHERE
 d.doc_id = 4
 --    d.niveau = 1
GROUP BY de.niveau) iceDoc
