import pymysql
conn = pymysql.connect(host='127.0.0.1', user='root', password='4XqKXdEjfW2k96k8', database='kairos_next_gen')
with conn.cursor() as cur:
    cur.execute("UPDATE report SET looks_generated=0, file_path=NULL WHERE report_id=42")
    conn.commit()
    print('Reset report 42 to pending')
conn.close()
