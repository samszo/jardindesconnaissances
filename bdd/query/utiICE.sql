
 SELECT login, nbDoc*nbTag*nbLien ME
 , FORMAT(1/(nbDoc*nbTag*nbLien),10) ICE
 , nbDoc, nbTag, poids
 , nbLien
 FROM (

SELECT u.login,
         count(DISTINCT utd.doc_id) nbDoc,
         count(DISTINCT utd.tag_id) nbTag,
         sum(utd.poids) poids,
         COUNT(DISTINCT utd.utitagdoc_id) nbLien		 
    FROM flux_uti u
         INNER JOIN flux_utitagdoc utd ON utd.uti_id = u.uti_id
GROUP BY u.uti_id
  ) c
  ORDER BY ME DESC
