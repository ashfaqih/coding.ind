import streamlit as st
import pandas as pd
import numpy as np
import joblib
from datetime import datetime
import calendar
import io

# Konfigurasi halaman
st.set_page_config(
    page_title="Prediksi Musim Buah - Gudang Buah",
    layout="wide"
)

# Kamus data musiman
SEASONAL_DATA = {
    (1, 2, 3): ['Durian', 'Rambutan', 'Alpukat', 'Manggis', 'Sawo', 'Kedondong', 
                'Salak', 'Jambu Biji', 'Jeruk Nipis', 'Duku', 'Jeruk Bali', 'Sirsak'],
    (4, 5, 6): ['Kesemek', 'Jeruk Manis', 'Salak', 'Jeruk Nipis', 'Duku', 'Jeruk Bali',
                'Kedondong', 'Jambu Biji', 'Jambu Air'],
    (7, 8, 9): ['Kesemek', 'Jeruk Manis', 'Belimbing', 'Melon', 'Jambu Mete',
                'Jambu Bol', 'Mangga', 'Jambu Air', 'Jeruk Bali', 'Jambu Biji', 'Kedondong'],
    (10, 11, 12): ['Durian', 'Manggis', 'Rambutan', 'Alpukat', 'Sawo', 'Jeruk Bali']
}

def get_seasonal_fruits(month):
    for period, fruits in SEASONAL_DATA.items():
        if month in period:
            return fruits
    return []

def validate_csv_format(df):
    required_columns = ['ID', 'Nama Buah', 'Kategori', 'Satuan', 
                       'Stok Awal', 'Stok Masuk', 'Stok Keluar', 
                       'Stok Akhir', 'Tanggal Masuk']
    
    # Periksa apakah semua kolom yang diperlukan ada
    if not all(col in df.columns for col in required_columns):
        return False
    
    # Periksa tipe data
    try:
        df['Tanggal Masuk'] = pd.to_datetime(df['Tanggal Masuk'])
        df[['Stok Awal', 'Stok Masuk', 'Stok Keluar', 'Stok Akhir']] = \
            df[['Stok Awal', 'Stok Masuk', 'Stok Keluar', 'Stok Akhir']].astype(float)
        return True
    except:
        return False

def load_data(uploaded_file):
    try:
        # Baca file CSV yang diunggah
        df = pd.read_csv(uploaded_file)
        
        # Validasi format
        if not validate_csv_format(df):
            st.error("Maaf format csv yang diupload tidak sesuai dengan yang diinginkan.")
            return None
            
        # Ubah Nama Buah menjadi title case dan ganti underscore dengan spasi
        df['Nama Buah'] = df['Nama Buah'].str.title().str.replace('_', ' ')
        
        # Ubah Tanggal Masuk menjadi datetime dan format ulang
        df['Tanggal Masuk'] = pd.to_datetime(df['Tanggal Masuk']).dt.strftime('%Y-%m-%d')
        
        return df
    except Exception as e:
        st.error("Maaf format csv yang diupload tidak sesuai dengan yang diinginkan.")
        return None

def predict_stock(nama_buah, month, historical_data):
    try:
        # Muat model
        model = joblib.load('rcmmodel.joblib')
        
        # Filter data untuk buah tertentu
        fruit_data = historical_data[historical_data['Nama Buah'] == nama_buah].copy()
        
        if len(fruit_data) == 0:
            return None
        
        # Hitung fitur-fitur
        avg_stok_masuk = fruit_data['Stok Masuk'].astype(float).mean()
        std_stok_masuk = fruit_data['Stok Masuk'].astype(float).std()
        avg_stok_keluar = fruit_data['Stok Keluar'].astype(float).mean()
        std_stok_keluar = fruit_data['Stok Keluar'].astype(float).std()
        
        # Periksa musiman
        is_seasonal = 1 if nama_buah in get_seasonal_fruits(month) else 0
        
        # Buat array fitur
        features = np.array([[avg_stok_masuk, std_stok_masuk, 
                            avg_stok_keluar, std_stok_keluar, is_seasonal]])
        
        # Buat prediksi
        prediction = model.predict(features)[0]
        
        return int(np.ceil(prediction))
    except Exception as e:
        st.error(f"Error dalam prediksi: {str(e)}")
        return None

def show_predictions(month, month_name, historical_data):
    st.header(f"Prediksi Bulan {month_name}")
    
    # Dapatkan buah musiman untuk bulan yang dipilih
    seasonal_fruits = get_seasonal_fruits(month)
    
    # Bagian Buah Musiman
    st.subheader("Buah Musiman")
    seasonal_container = st.container()
    with seasonal_container:
        for fruit in seasonal_fruits:
            prediction = predict_stock(fruit.title(), month, historical_data)
            if prediction:
                st.info(f"{fruit}: {prediction} unit")

    # Bagian Buah Lainnya
    st.subheader("Rekomendasi Stok Lainnya")
    other_container = st.container()
    with other_container:
        all_fruits = historical_data['Nama Buah'].unique()
        other_fruits = [f for f in all_fruits if f not in [x.title() for x in seasonal_fruits]]
        for fruit in other_fruits:
            prediction = predict_stock(fruit, month, historical_data)
            if prediction:
                st.success(f"{fruit}: {prediction} unit")

def main():
    st.title("Prediksi Musim Buah")

    # Pengunggah file
    uploaded_file = st.file_uploader("Upload file CSV transaksi", type=['csv'])
    
    if uploaded_file is not None:
        # Muat data historis dari CSV yang diunggah
        historical_data = load_data(uploaded_file)
        
        if historical_data is not None:
            # Dapatkan bulan sekarang dan bulan depan
            current_month = datetime.now().month
            next_month = current_month % 12 + 1
            
            # Dapatkan nama bulan
            current_month_name = calendar.month_name[current_month]
            next_month_name = calendar.month_name[next_month]

            # Buat dropdown untuk pemilihan bulan
            selected_month = st.selectbox(
                "Pilih Bulan Prediksi",
                ["Bulan Ini", "Bulan Depan"],
                index=0
            )

            # Tampilkan prediksi berdasarkan pilihan
            if selected_month == "Bulan Ini":
                show_predictions(current_month, current_month_name, historical_data)
            else:
                show_predictions(next_month, next_month_name, historical_data)

            # Tambahkan informasi tambahan
            st.markdown("---")
            st.markdown("""
            ### Catatan:
            - Prediksi stok didasarkan pada data historis dan faktor musiman
            - Model mempertimbangkan rata-rata stok masuk dan keluar
            - Buah musiman memiliki rekomendasi stok yang lebih tinggi
            """)

            # Tambahkan bagian preview data
            st.markdown("---")
            st.subheader("Preview Data Transaksi")
            st.dataframe(historical_data)

if __name__ == "__main__":
    main()
