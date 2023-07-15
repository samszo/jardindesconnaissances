SELECT exiRootId, exiRootNom
,niveau, nbExiEnfNiv, niveau*nbExiEnfNiv iceExi
FROM(
SELECT 
    e.exi_id exiRootId,
    e.nom exiRootNom,
    e.niveau exiRootNiveau,
    (ee.niveau - e.niveau + 1) niveau,
    COUNT(DISTINCT ee.exi_id) nbExiEnfNiv
FROM
    flux_exi e
        INNER JOIN
    flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
WHERE
    e.niveau = 1
GROUP BY ee.niveau) iceExi