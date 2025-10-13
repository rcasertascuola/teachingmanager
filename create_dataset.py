import pandas as pd
import numpy as np

# Create a dictionary with the data
data = {
    'brand': np.random.choice(['BrandA', 'BrandB', 'BrandC', 'BrandD', 'BrandE'], size=100),
    'prezzo': np.random.randint(150, 1200, size=100),
    'batteria_mAh': np.random.choice([3000, 4000, 5000, 6000], size=100),
    'memoria_GB': np.random.choice([64, 128, 256, 512], size=100)
}

# Create a dataframe
df = pd.DataFrame(data)

# Save the dataframe to a csv file
df.to_csv('smartphones.csv', index=False)

print("Dataset 'smartphones.csv' created successfully.")
