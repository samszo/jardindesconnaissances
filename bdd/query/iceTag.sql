SELECT tagRootId, tagRootTitre
,niveau, nbTagEnfNiv, niveau*nbTagEnfNiv iceNiv
FROM(
SELECT 
    t.tag_id tagRootId,
    t.code tagRootTitre,
    t.niveau tagRootNiveau,
    (te.niveau - t.niveau + 1) niveau,
    COUNT(DISTINCT te.tag_id) nbTagEnfNiv
FROM
    flux_tag t
        INNER JOIN
    flux_tag te ON te.lft BETWEEN t.lft AND t.rgt
WHERE
    t.niveau = 1
 GROUP BY te.niveau
--    t.niveau = 2
-- GROUP BY t.code
) iceTag
