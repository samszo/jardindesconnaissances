SELECT `td2`.tag_id, `td3`.tag_id, dP.titre, `d`.note
FROM `flux_tagdoc` AS `td`
 INNER JOIN `flux_doc` AS `d` ON d.doc_id = td.doc_id
 INNER JOIN `flux_doc` AS `dP` ON dP.doc_id = d.tronc
 INNER JOIN `flux_tagdoc` AS `td1` ON td1.doc_id = td.doc_id 
 LEFT JOIN `flux_tagdoc` AS `td2` ON td2.doc_id = td.doc_id AND td2.tag_id = 10
 LEFT JOIN `flux_tagdoc` AS `td3` ON td3.doc_id = td.doc_id AND td3.tag_id = 288 
 INNER JOIN `flux_tag` AS `t` ON t.tag_id = td1.tag_id AND (t.code LIKE '%intelligence%' OR  t.code LIKE '%collective%' )   
GROUP BY `d`.`doc_id`
HAVING td2.tag_id = 10 AND td3.tag_id = 288
ORDER BY `dP`.`titre` ASC