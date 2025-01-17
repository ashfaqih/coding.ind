import streamlit as st
import pandas as pd
import numpy as np
import joblib
from datetime import datetime
import calendar

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

def load_data():
    try:
        # Read CSV file
        df = pd.read_csv('transaction_history.csv')
        # Convert nama_buah to title case and replace underscore with space
        df['nama_buah'] = df['nama_buah'].str.title().str.replace('_', ' ')
        # Convert tanggal_masuk to datetime
        df['tanggal_masuk'] = pd.to_datetime(df['tanggal_masuk'])
        return df
    except Exception as e:
        st.error(f"Error loading data: {str(e)}")
        return None

def predict_stock(nama_buah, month, historical_data):
    try:
        # Load the model
        model = joblib.load('rcmmodel.joblib')
        
        # Filter data for the specific fruit
        fruit_data = historical_data[historical_data['nama_buah'] == nama_buah].copy()
        
        if len(fruit_data) == 0:
            return None
        
        # Calculate features
        avg_stok_masuk = fruit_data['stok_masuk'].astype(float).mean()
        std_stok_masuk = fruit_data['stok_masuk'].astype(float).std()
        avg_stok_keluar = fruit_data['stok_keluar'].astype(float).mean()
        std_stok_keluar = fruit_data['stok_keluar'].astype(float).std()
        
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

def main():
    st.title("Prediksi Musim Buah")

    # Get current and next month
    current_month = datetime.now().month
    next_month = current_month % 12 + 1
    
    # Get month names
    current_month_name = calendar.month_name[current_month]
    next_month_name = calendar.month_name[next_month]

    # Load historical data from CSV
    historical_data = load_data()
    if historical_data is None:
        st.error("Gagal memuat data dari CSV. Pastikan file transaction_history.csv tersedia.")
        return

    # Create two columns for current and next month predictions
    col1, col2 = st.columns(2)

    # Current Month Predictions
    with col1:
        st.header(f"Prediksi Bulan Ini ({current_month_name})")
        
        # Get seasonal fruits for current month
        current_seasonal = get_seasonal_fruits(current_month)
        
        # Seasonal Fruits Section
        st.subheader("Buah Musiman")
        seasonal_container = st.container()
        with seasonal_container:
            for fruit in current_seasonal:
                prediction = predict_stock(fruit.title(), current_month, historical_data)
                if prediction:
                    st.info(f"{fruit}: {prediction} unit")

        # Other Fruits Section
        st.subheader("Rekomendasi Stok Lainnya")
        other_container = st.container()
        with other_container:
            all_fruits = historical_data['nama_buah'].unique()
            other_fruits = [f for f in all_fruits if f not in [x.title() for x in current_seasonal]]
            for fruit in other_fruits:
                prediction = predict_stock(fruit, current_month, historical_data)
                if prediction:
                    st.success(f"{fruit}: {prediction} unit")

    # Next Month Predictions
    with col2:
        st.header(f"Prediksi Bulan Depan ({next_month_name})")
        
        # Get seasonal fruits for next month
        next_seasonal = get_seasonal_fruits(next_month)
        
        # Seasonal Fruits Section
        st.subheader("Buah Musiman")
        seasonal_container = st.container()
        with seasonal_container:
            for fruit in next_seasonal:
                prediction = predict_stock(fruit.title(), next_month, historical_data)
                if prediction:
                    st.info(f"{fruit}: {prediction} unit")

        # Other Fruits Section
        st.subheader("Rekomendasi Stok Lainnya")
        other_container = st.container()
        with other_container:
            all_fruits = historical_data['nama_buah'].unique()
            other_fruits = [f for f in all_fruits if f not in [x.title() for x in next_seasonal]]
            for fruit in other_fruits:
                prediction = predict_stock(fruit, next_month, historical_data)
                if prediction:
                    st.success(f"{fruit}: {prediction} unit")

    # Add some additional information
    st.markdown("---")
    st.markdown("""
    ### Catatan:
    - Prediksi stok didasarkan pada data historis dan faktor musiman
    - Model mempertimbangkan rata-rata stok masuk dan keluar
    - Buah musiman memiliki rekomendasi stok yang lebih tinggi
    - Data diambil dari file transaction_history.csv
    """)

    # Add data preview section
    st.markdown("---")
    st.subheader("Preview Data Transaksi")
    st.dataframe(historical_data)

if __name__ == "__main__":
    main()