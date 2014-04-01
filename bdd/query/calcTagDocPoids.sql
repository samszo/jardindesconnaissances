--﻿TRUNCATE TABLE  flux_tagdoc;
INSERT INTO flux_tagdoc (tag_id, doc_id, poids)
SELECT utd.tag_id, utd.doc_id, count(distinct uti_id) nb
FROM flux_utitagdoc utd
group by utd.tag_id, utd.doc_id;