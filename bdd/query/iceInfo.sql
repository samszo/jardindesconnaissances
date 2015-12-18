SELECT *
,(nbDoc1*nbUti1*nbTag1*nbLien1) ice1
,(nbDoc2*nbUti2*nbTag2*nbLien2) ice2
,(nbDoc3*nbUti3*nbTag3*nbLien3) ice3
FROM
(SELECT 
         count(DISTINCT utd.doc_id) nbDoc1,
         count(DISTINCT utd.uti_id) nbUti1,
		 count(DISTINCT utd.tag_id) nbTag1,
         -- SUM(utd.poids) poids1,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien1
    FROM flux_utitagdoc utd ) utd1
,(SELECT 
         count(DISTINCT utd.doc_id) nbDoc2,
         count(DISTINCT utd.uti_id) nbUti2,
		 count(DISTINCT utd.tag_id) nbTag2,
         -- SUM(utd.poids) poids2,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien2
    FROM flux_utitagdoc utd 
	INNER JOIN flux_doc d ON d.doc_id = utd.doc_id AND d.tronc = ""
	) utd2
,(SELECT 
         count(DISTINCT utd.doc_id) nbDoc3,
         count(DISTINCT utd.uti_id) nbUti3,
		 count(DISTINCT utd.tag_id) nbTag3,
         -- SUM(utd.poids) poids,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien3
    FROM flux_utitagdoc utd 
	INNER JOIN flux_doc d ON d.doc_id = utd.doc_id AND d.tronc = "" AND MATCH(d.note, d.titre) AGAINST('information communication')
	) utd3
