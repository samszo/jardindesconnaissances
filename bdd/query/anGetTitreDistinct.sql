SELECT 
    COUNT(DISTINCT d.doc_id) somme

--    COUNT(*) nb,
--    COUNT(DISTINCT d.doc_id) nbD,
--    COUNT(DISTINCT dg.doc_id) nbG,
--    COUNT(DISTINCT d.doc_id) - COUNT(DISTINCT dg.doc_id) nbDiff,
--    GROUP_CONCAT(d.doc_id) ids,
--    d.titre
--    SUBSTRING(d.titre, (LOCATE("/",d.titre)+2)) court
--    GROUP_CONCAT(dg.parent) idsG
FROM
    flux_doc d
        INNER JOIN
	flux_doc dg ON dg.parent = d.doc_id
        AND dg.titre = 'Flux_Glanguage::sauveAnalyseTexte'
-- WHERE
	-- dg.doc_id is null and
    -- d.titre NOT LIKE 'Photo %' AND d.titre NOT LIKE 'Flux_Glanguage::sauveAnalyseTexte' AND d.titre NOT LIKE 'analyzeSyntax %' AND d.titre != 'Flux_Glanguage' AND d.titre != 'Manques' AND d.titre NOT LIKE 'manque : %'
-- GROUP BY d.titre -- SUBSTRING(d.titre, (LOCATE("/",d.titre)+2))
-- ORDER BY nb desc