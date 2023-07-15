TRUNCATE TABLE  `flux_utitag`;
INSERT INTO flux_utitag (tag_id, uti_id, poids)
select utd.tag_id, uti_id, count(distinct utd.doc_id) nb
FROM flux_utitagdoc utd
group by utd.tag_id, uti_id;