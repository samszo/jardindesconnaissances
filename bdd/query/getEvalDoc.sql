SELECT d.doc_id
	, d.data
	, d.tronc
	, COUNT(DISTINCT utd.utitagdoc_id) nbEval
	, DATE_FORMAT(utd.maj, '%d-%c-%y %h:%i:%s') temps
	, GROUP_CONCAT(DISTINCT utd.tag_id) idsTag
	, GROUP_CONCAT(DISTINCT utd.uti_id) idsUti
	, GROUP_CONCAT(DISTINCT utd.poids) dPoids
    , SUM(utd.poids) poids
FROM flux_doc d
	INNER JOIN flux_utitagdoc utd ON utd.doc_id = d.doc_id
WHERE d.titre = 'axesEval'
 GROUP BY d.data 
 order by temps