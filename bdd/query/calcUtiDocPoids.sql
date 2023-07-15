TRUNCATE TABLE  `flux_utidoc`;
INSERT INTO flux_utidoc (doc_id, uti_id, maj, poids)
select doc_id, uti_id, maj, count(distinct tag_id) nb
FROM flux_utitagdoc utd
group by doc_id, uti_id;