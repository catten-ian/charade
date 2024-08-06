import mysql.connector

# connect to database
conn = mysql.connector.connect(
    host="localhost",
    port=3307,
    user="charade",
    password="pwdtest1",
    database="chdb",
    charset='utf8mb3'
)

cursor = conn.cursor()

# create table if not exists
cursor.execute("CREATE TABLE IF NOT EXISTS tb_words (id INT AUTO_INCREMENT PRIMARY KEY, word VARCHAR(255) UNIQUE)")

# read words from file
with open("C:\\Users\\lth75\\Documents\\1. 巴洛克.txt", "r", encoding='utf-8') as f:
    words = f.readlines()

# insert words into table
for word in words:
    if '.' in word:
        word = word.split('.')[1]  # 如果词语格式为'数字.词语'，只操作'词语部分'
    word = word.strip()
    cursor.execute("INSERT IGNORE INTO tb_words (word) VALUES (%s)", (word,))

# submit changes to database
conn.commit()

# close cursor and connection
cursor.close()
conn.close()
