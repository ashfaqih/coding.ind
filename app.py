import streamlit as st
import pandas as pd
import numpy as np
import joblib
from datetime import datetime
import calendar
import io

# Page configuration
st.set_page_config(
    page_title="Prediksi Musim Buah - Gudang Buah",
    layout="wide"
)

# Seasonal data dictionary
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
    
    # Check if all required columns exist
    if not all(col in df.columns for col in required_columns):
        return False
    
    # Check data types
    try:
        df['Tanggal Masuk'] = pd.to_datetime(df['Tanggal Masuk'])
        df[['Stok Awal', 'Stok Masuk', 'Stok Keluar', 'Stok Akhir']] = \
            df[['Stok Awal', 'Stok Masuk', 'Stok Keluar', 'Stok Akhir']].astype(float)
        return True
    except:
        return False

def load_data(uploaded_file):
    try:
        # Read uploaded CSV file
        df = pd.read_csv(uploaded_file)
        
        # Validate format
        if not validate_csv_format(df):
            st.error("Maaf format csv yang diupload tidak sesuai dengan yang diinginkan.")
            return None
            
        # Convert Nama Buah to title case and replace underscore with space
        df['Nama Buah'] = df['Nama Buah'].str.title().str.replace('_', ' ')
        
        # Convert Tanggal Masuk to datetime and format it
        df['Tanggal Masuk'] = pd.to_datetime(df['Tanggal Masuk']).dt.strftime('%Y-%m-%d')
        
        return df
    except Exception as e:
        st.error("Maaf format csv yang diupload tidak sesuai dengan yang diinginkan.")
        return None

def predict_stock(nama_buah, month, historical_data):
    try:
        # Load the model
        model = joblib.load('rcmmodel.joblib')
        
        # Filter data for the specific fruit
        fruit_data = historical_data[historical_data['Nama Buah'] == nama_buah].copy()
        
        if len(fruit_data) == 0:
            return None
        
        # Calculate features
        avg_stok_masuk = fruit_data['Stok Masuk'].astype(float).mean()
        std_stok_masuk = fruit_data['Stok Masuk'].astype(float).std()
        avg_stok_keluar = fruit_data['Stok Keluar'].astype(float).mean()
        std_stok_keluar = fruit_data['Stok Keluar'].astype(float).std()
        
        # Check seasonality
        is_seasonal = 1 if nama_buah in get_seasonal_fruits(month) else 0
        
        # Create feature array
        features = np.array([[avg_stok_masuk, std_stok_masuk, 
                            avg_stok_keluar, std_stok_keluar, is_seasonal]])
        
        # Make prediction
        prediction = model.predict(features)[0]
        
        return int(np.ceil(prediction))
    except Exception as e:
        st.error(f"Error in prediction: {str(e)}")
        return None

def show_predictions(month, month_name, historical_data):
    st.header(f"Prediksi Bulan {month_name}")
    
    # Get seasonal fruits for the selected month
    seasonal_fruits = get_seasonal_fruits(month)
    
    # Seasonal Fruits Section
    st.subheader("Buah Musiman")
    seasonal_container = st.container()
    with seasonal_container:
        for fruit in seasonal_fruits:
            prediction = predict_stock(fruit.title(), month, historical_data)
            if prediction:
                st.info(f"{fruit}: {prediction} unit")

    # Other Fruits Section
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

    # File uploader
    uploaded_file = st.file_uploader("Upload file CSV transaksi", type=['csv'])
    
    if uploaded_file is not None:
        # Load historical data from uploaded CSV
        historical_data = load_data(uploaded_file)
        
        if historical_data is not None:
            # Get current and next month
            current_month = datetime.now().month
            next_month = current_month % 12 + 1
            
            # Get month names
            current_month_name = calendar.month_name[current_month]
            next_month_name = calendar.month_name[next_month]

            # Create dropdown for month selection
            selected_month = st.selectbox(
                "Pilih Bulan Prediksi",
                ["Bulan Ini", "Bulan Depan"],
                index=0
            )

            # Show predictions based on selection
            if selected_month == "Bulan Ini":
                show_predictions(current_month, current_month_name, historical_data)
            else:
                show_predictions(next_month, next_month_name, historical_data)

            # Add some additional information
            st.markdown("---")
            st.markdown("""
            ### Catatan:
            - Prediksi stok didasarkan pada data historis dan faktor musiman
            - Model mempertimbangkan rata-rata stok masuk dan keluar
            - Buah musiman memiliki rekomendasi stok yang lebih tinggi
            """)

            # Add data preview section
            st.markdown("---")
            st.subheader("Preview Data Transaksi")
            st.dataframe(historical_data)

if __name__ == "__main__":
    main()
