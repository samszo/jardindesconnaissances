SELECT `d`.*, `u`.*, `t`.*, utd.* 
FROM `flux_utitagdoc` AS `utd` 
 INNER JOIN `flux_doc` AS `d` ON d.doc_id=utd.doc_id
 INNER JOIN `flux_uti` AS `u` ON u.uti_id = utd.uti_id
 INNER JOIN `flux_tag` AS `t` ON t.tag_id = utd.tag_id 
-- WHERE (u.login = 'luckysemiosis') AND (t.code = 'annotation')
 WHERE (t.code = 'actulivre') and d.url like '%amazon%'
 ORDER BY `utd`.`maj` ASC