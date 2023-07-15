SELECT 
    d.doc_id, d.url, d.titre,
--    count(distinct dV.doc_id) nb
	dVG.doc_id, dVG.titre, dVG.note
FROM
    flux_doc d
    INNER JOIN 
    flux_valarnum.flux_doc dV on dV.url = d.url and dV.titre = d.titre
    INNER JOIN 
    flux_valarnum.flux_doc dVG on dVG.parent = dV.doc_id AND dVG.titre = 'Flux_Glanguage::sauveAnalyseTexte'
WHERE dVG.note != ''
-- group by d.url, d.titre
order by  d.doc_id