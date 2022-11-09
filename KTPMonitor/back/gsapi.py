import gspread
from oauth2client.service_account import ServiceAccountCredentials

scope = ['https://spreadsheets.google.com/feeds', 'https://www.googleapis.com/auth/drive']
creds = ServiceAccountCredentials.from_json_keyfile_name('secret/gs cred.json', scope)
client = None

def authorize():
    global client, sheet
    client = gspread.authorize(creds)

def get_data(name, page, columns):
    sheet = client.open(name).get_worksheet(page)
    res = []
    for col in columns:
        res.append(sheet.col_values(col))
    return res