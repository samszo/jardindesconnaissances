select t.tag_id, t.code, count(distinct ttt.code) nbType, group_concat(distinct ttt.code) types
FROM flux_tag t
inner join flux_utitag ut on ut.tag_id = t.tag_id  and ut.uti_id = 5
inner join flux_tagtag tt on tt.tag_id_src = t.tag_id
inner join flux_tag ttt on ttt.tag_id = tt.tag_id_dst
inner join flux_utitag utt on utt.tag_id = ttt.tag_id  and utt.uti_id = 5
group by t.tag_id