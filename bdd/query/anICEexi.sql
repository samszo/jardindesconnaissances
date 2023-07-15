SELECT 
    COUNT(DISTINCT ee.exi_id) nb,
    ee.niveau + 1 - e.niveau niv,
    COUNT(DISTINCT ee.exi_id) * (ee.niveau + 1 - e.niveau) complexite
FROM
    flux_exi e
        INNER JOIN
    -- flux_exi ee ON ee.lft BETWEEN e.lft AND e.rgt
    flux_exi ee ON ee.exi_id = e.exi_id
WHERE
    e.exi_id IN (668 , 285, 60)
GROUP BY ee.niveau + 1 - e.niveau
   
